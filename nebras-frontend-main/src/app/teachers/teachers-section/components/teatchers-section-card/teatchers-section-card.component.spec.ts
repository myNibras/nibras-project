import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TeatchersSectionCardComponent } from './teatchers-section-card.component';

describe('TeatchersSectionCardComponent', () => {
  let component: TeatchersSectionCardComponent;
  let fixture: ComponentFixture<TeatchersSectionCardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [TeatchersSectionCardComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TeatchersSectionCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
